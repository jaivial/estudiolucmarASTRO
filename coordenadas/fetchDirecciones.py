import mysql.connector
import json

# Database connection parameters
db_config = {
    'host': '127.0.0.1',
    'port': 3308,
    'user': 'root',
    'password': '',
    'database': 'u212050690_estudiolucmar'
}

# Connect to the database
try:
    connection = mysql.connector.connect(**db_config)
    cursor = connection.cursor()
    print("Connected to the database")

    # Query to fetch 'direccion' column from 'inmuebles' table
    query = "SELECT direccion FROM inmuebles"
    cursor.execute(query)
    
    # Fetch all rows from the executed query
    rows = cursor.fetchall()
    
    # Extract 'direccion' from rows and store in a list
    direcciones = [row[0] for row in rows]
    
    # Save the list of 'direccion' to a JSON file
    with open('direcciones.json', 'w') as json_file:
        json.dump(direcciones, json_file, ensure_ascii=False, indent=4)
    
    print("Data has been saved to 'direcciones.json'")

except mysql.connector.Error as err:
    print(f"Error: {err}")

finally:
    if connection.is_connected():
        cursor.close()
        connection.close()
        print("MySQL connection is closed")
