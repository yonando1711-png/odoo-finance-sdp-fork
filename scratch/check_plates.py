import pandas as pd
import os

plates = [
    'B9421BXG', 'B9400BXG', 'B9404BXG', 'B9425BXG', 'B9406BXG',
    'B9402BXG', 'B9416BXG', 'B9431BXG', 'B9433BXG', 'B9422BXG', 'B9480BXE'
]

files = [
    'Cetakan Invoice Rental.xlsx',
    'Sales Order - Invoice Sample.xlsx',
    'Cetakan Invoice Driver.xlsx',
    'Cetakan Invoice Others with tax or no tax.xlsx'
]

for file in files:
    if os.path.exists(file):
        print(f"Checking {file}...")
        try:
            df = pd.read_excel(file)
            for plate in plates:
                if df.apply(lambda row: row.astype(str).str.contains(plate).any(), axis=1).any():
                    print(f"  Found {plate} in {file}!")
        except Exception as e:
            print(f"  Error reading {file}: {e}")
