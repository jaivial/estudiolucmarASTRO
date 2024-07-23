import json
import mysql.connector

# MySQL database credentials
host = "127.0.0.1"
port = "3308"
user = "root"
password = ""
database = "u212050690_estudiolucmar"

# Connect to MySQL
try:
    conn = mysql.connector.connect(
        host=host,
        port=port,
        user=user,
        password=password,
        database=database
    )

    if conn.is_connected():
        print("Connected to MySQL database")

        # Load addresses from updated_direcciones.json
        with open('updated_direcciones.json', 'r', encoding='utf-8') as f:
            direcciones_data = json.load(f)

        cursor = conn.cursor()

        # Iterate through addresses and update database
        for direccion in direcciones_data:
            address = direccion.get('address')
            latlng = direccion.get('latlng')

            if address and latlng:
                # Convert latlng list to JSON string
                latlng_json = json.dumps({"latitude": latlng[0], "longitude": latlng[2]})

                # Query to update database
                update_query = """
                    UPDATE inmuebles
                    SET coordinates = JSON_SET(coordinates, '$.latlng', %s)
                    WHERE direccion = %s
                """
                # Execute update query
                cursor.execute(update_query, (latlng_json, address))
                conn.commit()
                print(f"Updated coordinates for address: {address}")

        cursor.close()

except mysql.connector.Error as e:
    print(f"Error connecting to MySQL: {e}")

finally:
    if conn and conn.is_connected():
        conn.close()
        print("MySQL connection closed")
