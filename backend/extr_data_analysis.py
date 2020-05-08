#!/usr/bin/env python3

import mysql.connector
import openpyxl
import argparse
import glob
import os
import sys
from locale import atof, setlocale, LC_NUMERIC, LC_ALL
from itertools import islice
import pandas as pd
setlocale(LC_NUMERIC,('it_IT','UTF-8'))



def na2none(a):
	
	if a == 'N/A':
		return None
	else:

		return float(a)


parser = argparse.ArgumentParser()
parser.add_argument('--data_folder', help='his is the folder containing all the Analisi Excell files. One file per plate.', required=True)
parser.add_argument('--control_wells', help="manual setup for control wells in the plate (list, space separated. Esample: A01 A02 A03 ...)", default=[], nargs='+')


parser.add_argument('--kit', default="bosphore")
parser.add_argument('--kf', action='store_true',help='Equals to --control_wells A01 A02 A12 D04 D08')
parser.add_argument('--old_plates', action='store_true',help='Equals to --control_wells H01 A01 A02 A05 A08 A12')



parser.add_argument('--well_allow', default=[], nargs='+', help="Allows to override the plate setup to include a sample in an otherwise 'control' well")
parser.add_argument('--platename_from_file',action='store_true', help='allows to take the Plate Name from the filename, instead thatn from the designated cell in the template')




args = parser.parse_args()

control_samples = ['NTC','PK','PE','BE','55555','77777','11111111','33333','20202020','25252525','27272727', 'pcr NEG','pcr POS']

fluorophores=["FAM","HEX","Cy5"]

if args.kf:
	ListOfControlWells=['A01','A02','A12','D04','D08']
elif args.old_plates:
	ListOfControlWells=['H01','A01','A02','A05','A08','A12']
else:
	ListOfControlWells=list(args.control_wells)

WellsToAvoid = [_ for _ in ListOfControlWells if _ not in list(args.well_allow)]

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
curves2add=[]
samplesToCheck=[]

well2barcode={}
mycursor = mydb.cursor()

for analFile in glob.glob(args.data_folder+'/*.xls*'):

	wb_obj = openpyxl.load_workbook(analFile,data_only=True, read_only=True)

	try:
		sheet = wb_obj["analysis"]
	except:
		print("Error in finding Data tab", analFile )
		sys.exit(0)


	if not args.platename_from_file:
		plateName= sheet["C2"].value
		print("Open", analFile,plateName)
		if plateName == 'XXXy' and analFile == 'Analisi/200408/V220040801_analisi.xlsx': plateName = 'V220040801'
	else:
		plateName = os.path.basename(analFile).replace('_analisi.xlsm','')

	plateDate= '20'+plateName[2:4]+'-'+plateName[4:6]+'-'+plateName[6:8]

	fline=True
	for row in sheet.iter_rows():
		if fline:
			fline=False
			continue

		well_nozero= str(row[0].value)
		well= str(row[1].value)
			#target= str(row[3].value)
		barcode= str(row[3].value)
		isempty= int(row[8].value)
		is_control= int(barcode in control_samples or well in WellsToAvoid)
		batch_kf=str(row[4].value)


		#litref= row[9].value
		#cts= row[10].value[1:-1].split(';')

		

		val_cy5 = na2none(row[5].value)
		val_fam = na2none(row[6].value)
		val_hex = na2none(row[7].value)



		auto_result = row[12].value

		final_result = row[17].value if row[17].value != 'NON REFERTARE' else row[14].value

		if final_result not in ['POSITIVO','NEGATIVO','RIPETERE ESTRAZIONE','RIPETERE PCR','RIPETERE TAMPONE']:
			if is_control:
				final_result = 'CONTROLLO'
				auto_result = 'CONTROLLO'
			else:
				final_result = 'ERRORE COMPILAZIONE'

		if str (barcode) != "0" and barcode != 'None' and  not isempty:

			
			sql = 'SELECT 1 FROM samples WHERE barcode = %s'
			mycursor.execute(sql, (barcode,))
			mycursor.fetchone()
			if(mycursor.rowcount > 0):
						#print("OK", plateName,barcode,well,val_cy5,val_fam,val_hex,auto_result,final_result )
				samplesToAdd.append ( (plateName, plateDate,barcode,well,val_cy5,val_fam,val_hex,auto_result,final_result, is_control,batch_kf,args.kit))
				well2barcode[well_nozero] = barcode
			else:
				
				if str(barcode) != "0":
					samplesToCheck.append( (plateName, plateDate,barcode,well,val_cy5,val_fam,val_hex,auto_result,final_result, is_control))

			#if final_result not in ['POSITIVO','NEGATIVO','RIPETERE ESTRAZIONE','RIPETERE PCR','RIPETERE TAMPONE']:
				#print("NOK")



	print("Samples to Check: ",len(samplesToCheck))
	for k in samplesToCheck:
		print(k)

	

	for fluorophore in fluorophores:

		try:
			sheetFam = wb_obj["Curve "+fluorophore]
		except:
			print("Error in finding Curve "+fluorophore+" tab", analFile )
			sys.exit(0)
		
		data = sheetFam.values
		cols = next(data)[1:]
		data = list(data)
		idx = [r[0] for r in data]
		data = (islice(r, 1, None) for r in data)
		df = pd.DataFrame(data, index=idx, columns=cols).set_index('Cycle')
		

		for wellTo in df.columns:
			if wellTo in well2barcode:
				barcode = well2barcode[wellTo]
				wellDB = wellTo[0]+wellTo[1:].zfill(2)
				curveString = ','.join(map(str,list(df[wellTo])))
				curves2add.append( (plateName,wellDB,fluorophore,curveString) )
	

 

sql = 'INSERT IGNORE INTO pcr_plates (plate, data_pcr, barcode, well, Cy5, FAM, HEX, esito_automatico, esito_pcr, isControl,batch_kf,kit) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)'
#print(sql)
mycursor.executemany(sql, samplesToAdd)


sql = 'INSERT INTO curves (plate, well, fluorophore, curve) VALUES (%s,%s,%s,%s)'
mycursor.executemany(sql, curves2add)



mydb.commit()