# CIBIO-COVID

This repo contains the code developed for the intranet system to support the COVID-19 testing operations ad the DMA Lab of the University of Trento. The repo contains two main parts:

- A backend written in Python to allow for the operators to upload analysis files
- A frontend written in PHP/HTML/SQL to organize the data and to provide structured interface to them. This is currently deplyed at [http://colab1.cibio.unitn.it:8080/cibio-covid/index.php](http://colab1.cibio.unitn.it:8080/cibio-covid/index.php)

![](https://github.com/azufre451/cibio-covid/blob/master/TEMPLATES/img/example_covid_2.jpg)
*Frontend search page: one PCR sample is shown and the PCR curves are shown, together with the COVID-19 test outcome*

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

## Data Visualization ##

Available within UniTN VPN [Here](http://colab1.cibio.unitn.it:8080/cibio-covid/index.php).

It is possible to search:
- **By barcode** (or comma separated list of barcodes). This also shows the PCR curves for samples processed after May 7th
- **By PCR plate** (shows the layout of each plate, colored by COVID-Test outcome)
- **By PCR date** (shows all the PCRs performed on a given date)
- **Interative multi-sample search** (for barcode-reader equipped PCs in the laboratory area). This also sorts the scanned barcodes into two different lists (positive and negative samples).

## Data Upload ##

Files can be loaded into the Database with an online procedure available from within UniTN VPN [available here](http://colab1.cibio.unitn.it:8080/cibio-covid/load_data.php). Excel templates for data analysis are provided to the data-entry operators by the DMA Staff.

To upload a file, simply load it into the webpage and select the type of template you are loading:

![](https://github.com/azufre451/cibio-covid/blob/master/TEMPLATES/img/example_covid_upload1.jpg)

Once the upload is completed you should see a confirmation message like this:

![](https://github.com/azufre451/cibio-covid/blob/master/TEMPLATES/img/example_covid_upload2.jpg)

Alternatively, data can be manually loaded with two scripts in Python:

### Samples extraction template (extr_estrazioni.py)

`extr_estrazioni.py` populates the DB with the samples from the "Estrazioni" Excel template. It takes as input a folder with all the Excel files from the `estrazioni` procedure. The files contain the sample barcode and the date of processing.

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


### Samples Data Analysis template (extr_data_analysis.py)

`extr_data_analysis.py` processes `Analisi` files and populates the DB with the results of each PCR plate on each sample. The files must be in the right format as defined by the template.

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
extr_data_analysis.py --data_folder </path/to/your/analysis/folder>/200410/
```

Will load all the Analisi files in the folder `200410`

___

The code and templates in this repository were implemented and designed by *Moreno Zolfo*, *Tarcisio Fedrizzi*, *Serena Manara* and *Francesca Demichelis*
