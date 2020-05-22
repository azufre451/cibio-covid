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
#setlocale(LC_NUMERIC,('it_IT','UTF-8'))



def na2none(a):

	if a == 'N/A':
		return None
	else:

		return float(a)


parser = argparse.ArgumentParser()
parser.add_argument('--data_folder', help='his is the folder containing all the Analisi Excell files. One file per plate.', required=True)
parser.add_argument('--control_wells', help="manual setup for control wells in the plate (list, space separated. Esample: A01 A02 A03 ...)", default=[], nargs='+')
#parser.add_argument('--kit',default="bosphore")
parser.add_argument('--replace_data', action='store_true',  help="overwrite the data")
#parser.add_argument('--kf', action='store_true',help='Equals to --control_wells A01 A02 A12 D04 D08')
#parser.add_argument('--old_plates', action='store_true',help='Equals to --control_wells H01 A01 A02 A05 A08 A12')
parser.add_argument('--well_allow', default=[], nargs='+', help="Allows to override the plate setup to include a sample in an otherwise 'control' well")
parser.add_argument('--platename_from_file',action='store_true', help='allows to take the Plate Name from the filename, instead thatn from the designated cell in the template')




args = parser.parse_args()

#control_samples = ['NTC','PK','PE','BE','55555','77777','11111111','33333','20202020','25252525','27272727', 'pcr NEG','pcr POS']

fluorophores=["FAM","HEX", "Texas Red","Cy5"]

#if args.kf:
#	ListOfControlWells=['A01','A02','A12','D04','D08']
#elif args.old_plates:
#	ListOfControlWells=['H01','A01','A02','A05','A08','A12']
#else:
#	ListOfControlWells=list(args.control_wells)

#WellsToAvoid = [_ for _ in ListOfControlWells if _ not in list(args.well_allow)]

#print("Skipping wells: ",' '.join(WellsToAvoid))
#print("-- Pozzetti di Controllo: "+' '.join(WellsToAvoid))

try:


	mydb = mysql.connector.connect(
	  host="colab1.cibio.unitn.it",
	  user="covid_user",
	  passwd="Q2GtXNpnKj94IP4HEo0IyvCun",
	  database="covid",
	  port=33006,
	  auth_plugin='mysql_native_password'
	)

	mycursor = mydb.cursor()
	print("-- Connessione al Database riuscita")

except mysql.connector.Error as err:
	print("-- <span class=\"errorMessage\">ERRORE</span> del Database: ", str(err))


samplesToAdd=[]
curves2add=[]
samplesToCheck=[]
results_tracker={}

well2barcode={}

for analFile in glob.glob(args.data_folder+'/*.xls*'):

	wb_obj = openpyxl.load_workbook(analFile,data_only=True, read_only=True)

	try:
		sheet = wb_obj["analysis"]
	except:
		#print("Error in finding Data tab", analFile )
		print("-- ERRORE: il file non ha la tab 'analysis'")
		sys.exit(0)


	hh={}
	plateName=None
	for row in sheet.iter_rows():
		if len(hh) == 0:
			hh = {field.value: pos for (pos, field) in enumerate(row)}

			continue

		if plateName is None:
			if not args.platename_from_file:
				plateName= row[hh['plate']].value

				print("-- Apertura: <span class=\"emph\">", os.path.basename(analFile),'</span>')

				if plateName == 'XXXy' and analFile == 'Analisi/200408/V220040801_analisi.xlsx': plateName = 'V220040801'
			else:
				plateName = os.path.basename(analFile).replace('_analisi.xlsm','')

			plateDate= '20'+plateName[2:4]+'-'+plateName[4:6]+'-'+plateName[6:8]


		well_nozero= str(row[hh['well_no_zero']].value)
		well= str(row[hh['well_with_zero']].value)

		barcode= str(row[hh['barcode']].value)
		batch_kf=str(row[hh['batch']].value)

		val_cy5 = na2none(row[hh['cy5_cq']].value)
		val_fam = na2none(row[hh['fam_cq']].value)
		val_hex = na2none(row[hh['hex_cq']].value)
		val_tred = na2none(row[hh['texas_red_cq']].value)
		isempty= int(row[hh['is_well_empy']].value)
		is_control= int(row[hh['is_control']].value) # int(barcode in control_samples or well in WellsToAvoid)

		auto_result = row[hh['test_result_auto']].value

		final_result = row[hh['final_result']].value if row[hh['final_result']].value != 'NON REFERTARE' else row[hh['user_result']].value

		kit=str(row[hh['kit_name']].value)

		if final_result not in ['POSITIVO','NEGATIVO','RIPETERE ESTRAZIONE','RIPETERE PCR','RIPETERE TAMPONE']:
			if is_control:
				final_result = 'CONTROLLO'
				auto_result = 'CONTROLLO'
			elif final_result == 'CORRETTO' and auto_result == 'RIPETERE TAMPONE':
				final_result = 'RIPETERE TAMPONE'
			else:
				final_result = 'ERRORE COMPILAZIONE'

		if str (barcode) != "0" and barcode != 'None' and  not isempty:


			sql = 'SELECT 1 FROM samples WHERE barcode = %s'
			mycursor.execute(sql, (barcode,))
			mycursor.fetchone()
			if(mycursor.rowcount > 0):
						#print("OK", plateName,barcode,well,val_cy5,val_fam,val_hex,auto_result,final_result )
				samplesToAdd.append ( (plateName, plateDate,barcode,well,val_cy5,val_fam,val_hex,val_tred,auto_result,final_result, is_control,batch_kf,kit))
				well2barcode[well_nozero] = barcode
				if final_result not in results_tracker:
					results_tracker[final_result] = 1
				else:
					results_tracker[final_result] += 1
			else:

				if str(barcode) != "0":
					samplesToCheck.append( (plateName, plateDate,barcode,well,val_cy5,val_fam,val_hex,val_tred,auto_result,final_result, is_control))

			#if final_result not in ['POSITIVO','NEGATIVO','RIPETERE ESTRAZIONE','RIPETERE PCR','RIPETERE TAMPONE']:
				#print("NOK")
	if(len(samplesToAdd) > 0):
		print("-- ",len(samplesToAdd)," Campioni da aggiungere")
		for result,counter in results_tracker.items():
			print("----- ",counter," Campioni con esito ",result.lower())


	if(len(samplesToCheck) > 0):
		print("-- ",len(samplesToCheck)," Campioni con errori | <span class=\"errorMessage\">ATTENZIONE</span>")
		for k in samplesToCheck:
			print("----- Barcode: ",k[2], ', pozzetto ',k[3],' (',k[8],')')



	for fluorophore in fluorophores:

		try:
			sheetFam = wb_obj["Curve "+fluorophore]
		except:
			print("-- <span class=\"errorMessage\">ERRORE</span> - Non trovo la curva di ",fluorophore)


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
try:

	if args.replace_data:
		sql = 'DELETE FROM pcr_plates WHERE plate  = %s'
		mycursor.execute(sql, (plateName,))
		sql = 'DELETE FROM curves WHERE plate  = %s'
		mycursor.execute(sql, (plateName,))
		print("-- Eliminazione dati vecchia plate | <span class=\"okMessage\">OK</span>")


	sql = 'INSERT IGNORE INTO pcr_plates (plate, data_pcr, barcode, well, Cy5, FAM, HEX, TRed, esito_automatico, esito_pcr, isControl,batch_kf,kit) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)'
	mycursor.executemany(sql, samplesToAdd)
	print("-- Caricamento Curve | <span class=\"okMessage\">OK</span>")


	sql = 'INSERT IGNORE INTO curves (plate, well, fluorophore, curve) VALUES (%s,%s,%s,%s)'
	mycursor.executemany(sql, curves2add)
	print("-- Caricamento Plate | <span class=\"okMessage\">OK</span>")


	mydb.commit()
	if len(samplesToCheck) == 0:
		print("-- File caricato con successo, nessun errore! | <span class=\"okMessage\">OK</span>")
	else:
		print("-- File caricato con errori! | <span class=\"errorMessage\">ATTENZIONE</span>")

except mysql.connector.Error as err:
	print("-- <span class=\"errorMessage\">ERRORE</span> del Database: ", str(err))
