# cibio-covid
CIBIO Covid Code - Maintenance and Update scripts. This repo contains the code for the intranet system developed to support the COVID-19 testing operations ad the DMA Lab of the University of Trento.

![](https://github.com/azufre451/cibio-covid/blob/master/TEMPLATES/img/example_covid.jpg)

## Requirements

### For the frontend (DB sample search):
- PHP 7.2 (frontend)
- MySQL (frontend)

### For the backend (data loading):

- Python3
- openpyxl
- mysql-connector-python-rf
- mysql-connector-python

_Note_: Be sure to uninstall `mysql-connector` from your python installation (if present) and install `mysql-connector-python`


## extr_estrazioni

extr_estrazioni.py populates the DB with the samples from the "estrazioni" excel template. It takes as input a folder with all the Excel files from the `estrazioni` procedure.

```
usage: extr_estrazioni.py [-h] [--extr_folder EXTR_FOLDER]

optional arguments:
  -h, --help            show this help message and exit
  --extr_folder EXTR_FOLDER
                        path to the "Estrazioni" folder. The folder must
                        contain xlsx or xlsm files from the Estrazioni
                        pipeline. One file for each batch mubst pe provided
```

Example:

```
extr_estrazioni.py --extr_folder </path/to/your/extrazioni/folder>/200410/
```

Will load all the samples stored in `200410`


## extr_data_analysis.py 

This analyses the `Analisi` files to populate the DB with the results of each PCR plate. The files must be in the right format as defined by the template.

```
usage: extr_data_analysis.py [-h] --data_folder DATA_FOLDER
                             [--platename_from_file]

optional arguments:
  -h, --help            show this help message and exit
  --data_folder DATA_FOLDER
                        his is the folder containing all the Analisi Excell
                        files. One file per plate.

  --platename_from_file
                        allows to take the Plate Name from the filename,
                        instead than from the designated cell in the Excel template

```

Example:

```
extr_data_analysis.py --data_folder </path/to/your/extrazioni/folder>/200410/
```

Will load all the Analisi files in the folder `200410`

