#!/usr/bin/env python3

import mysql.connector
import openpyxl
import argparse
import glob
import sys
from conf import DBConf

parser = argparse.ArgumentParser()
parser.add_argument('--extr_folder', help='path to the "Estrazioni" folder. The folder must contain xlsx or xlsm files from the Estrazioni pipeline. One file per batch mubst pe present.',required=True)

args = parser.parse_args()


samplesToAdd=[]

for extratcionsFile in glob.glob(args.extr_folder+'/*.xls*'):
	
	print("-- Elaborazione Batch Estrazioni" ,extratcionsFile)

	wb_obj = openpyxl.load_workbook(extratcionsFile,data_only=True,read_only=True) 
	#sheet = wb_obj.active

	try:
		sheet = wb_obj["Accettazione"]
 
		batchName= sheet["B2"].value.split('_')[0]
		batchDate= sheet["B2"].value.split('_')[1]
	except:
		print("-- <span class=\"errorMessage\">ERRORE</span> | Non trovo la tab 'Accettazione' nel file caricato. Sicuro sia il file giusto?") 
		sys.exit(9)



	realDate = '20'+batchDate[0:2]+'-'+batchDate[2:4]+'-'+batchDate[4:6]


	for e in range(7,310):

		if sheet["B"+str(e)].value is not None:
			barcode= str(sheet["B"+str(e)].value)[0:8]
			samplesToAdd.append( (barcode, realDate,batchName) )


try:
	mydb = mysql.connector.connect(
		host=DBConf.colab_host,
		user=DBConf.colab_user,
		passwd=DBConf.colab_passwd,
		database=DBConf.colab_database,
		port=DBConf.colab_port,
		auth_plugin=DBConf.colab_auth_plugin
	)

	mycursor = mydb.cursor()

	print("-- Connessione al Database riuscita") 

	sql = "INSERT IGNORE INTO samples (barcode, data_checkin) VALUES (%s, %s)"
	mycursor.executemany(sql, [(_[0],_[1]) for _ in samplesToAdd ] )

	print("-- "+str(mycursor.rowcount)+" Nuovi Campioni caricati") 

	sql = "INSERT IGNORE INTO estrazioni (barcode, data_estrazione, batch) VALUES (%s, %s,%s)"
	mycursor.executemany(sql, samplesToAdd)

	print("-- "+str(mycursor.rowcount)+" Nuove Estrazioni caricate") 

	mydb.commit()
	
	print("-- <span class=\"okMessage\">OK</span> Procedura completata con successo")

except mysql.connector.Error as err:
  print("-- <span class=\"errorMessage\">ERRORE</span> del Database: ", str(err))




