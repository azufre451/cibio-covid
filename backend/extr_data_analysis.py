#!/usr/bin/env python3

import mysql.connector
import openpyxl
import argparse
import glob
import os
import sys
from locale import atof, setlocale, LC_NUMERIC
setlocale(LC_NUMERIC, '')

def na2none(a):
	if a == 'N/A':
		return None
	else:
		return float(atof(a))


parser = argparse.ArgumentParser()
parser.add_argument('--data_folder', help='his is the folder containing all the Analisi Excell files. One file per plate.', required=True)
parser.add_argument('--well_avoid', default=[], nargs='+')
parser.add_argument('--platename_from_file',action='store_true', help='allows to take the Plate Name from the filename, instead thatn from the designated cell in the template')




args = parser.parse_args()

control_wells = ['NTC','PK','PE','55555','77777']
WellsToAvoid = list(args.well_avoid)


print("Skipping wells: ",' '.join(WellsToAvoid))
mydb = mysql.connector.connect(
  host="colab1.cibio.unitn.it",
  user="covid_user",
  passwd="***REMOVED***",
  database="covid",
  port=33006,
  auth_plugin='mysql_native_password'
)


samplesToAdd=[]
samplesToCheck=[]
mycursor = mydb.cursor()

for analFile in glob.glob(args.data_folder+'/*.xls*'):

	wb_obj = openpyxl.load_workbook(analFile,data_only=True, read_only=True)

	try:
		sheet = wb_obj["Data"]
	except:
		print("Error in finding Data tab", analFile )
		sys.exit(0)


	if not args.platename_from_file:
		plateName= sheet["A2"].value
		print("Open", analFile,plateName)
		if plateName == 'XXXy' and analFile == 'Analisi/200408/V220040801_analisi.xlsx': plateName = 'V220040801'
	else:
		plateName = os.path.basename(analFile).replace('_analisi.xlsx','')

	plateDate= '20'+plateName[2:4]+'-'+plateName[4:6]+'-'+plateName[6:8]

	for row in sheet.iter_rows():
		if row[2].value == 'Cy5':
			well= str(row[1].value)
			target= str(row[3].value)
			barcode= str(row[5].value)
			is_control= int(barcode in control_wells or well in ['H01','A01','A02','A05','A08','A12'])



			litref= row[9].value
			cts= row[10].value[1:-1].split(';')



			val_cy5,val_fam,val_hex = map(na2none,cts)


			auto_result = row[16].value
			final_result = row[19].value if row[19].value != 'NON REFERTARE' else row[18].value
			if final_result not in ['POSITIVO','NEGATIVO','RIPETERE ESTRAZIONE','RIPETERE PCR']:
				if is_control:
					final_result = 'CONTROLLO'
					auto_result = 'CONTROLLO'
				else:
					final_result = 'ERRORE COMPILAZIONE'

			if well not in WellsToAvoid and str (barcode) != "0":
				#print(litref,cts,val_cy5,val_fam,val_hex)
				sql = 'SELECT 1 FROM samples WHERE barcode = %s'
				mycursor.execute(sql, (barcode,))
				mycursor.fetchone()
				if(mycursor.rowcount > 0):
						#print("OK", plateName,barcode,well,val_cy5,val_fam,val_hex,auto_result,final_result )
						samplesToAdd.append ( (plateName, plateDate,barcode,well,val_cy5,val_fam,val_hex,auto_result,final_result, is_control))

				else:
					if str(barcode) != "0":
						samplesToCheck.append( (plateName, plateDate,barcode,well,val_cy5,val_fam,val_hex,auto_result,final_result, is_control))



print("Samples to Check: ",len(samplesToCheck))
for k in samplesToCheck:
	print(k)

print("Samples to add: ",len(samplesToAdd))

sql = 'INSERT IGNORE INTO pcr_plates (plate, data_pcr, barcode, well, Cy5, FAM, HEX, esito_automatico, esito_pcr, isControl) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)'
#print(sql)
mycursor.executemany(sql, samplesToAdd)



mydb.commit()