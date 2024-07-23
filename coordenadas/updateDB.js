import mysql from 'mysql';
import fs from 'fs';

// MySQL database credentials
const host = '127.0.0.1';
const port = '3308'; // Make sure this matches your MySQL server port
const user = 'root';
const password = '';
const database = 'u212050690_estudiolucmar';

// Read and parse the JSON file
const jsonData = JSON.parse(fs.readFileSync('updated_direcciones.json', 'utf8'));

// Create a connection to the database
const connection = mysql.createConnection({
  host: host,
  port: port,
  user: user,
  password: password,
  database: database,
});

// Connect to the database
connection.connect((err) => {
  if (err) {
    console.error('Error connecting to database: ' + err.stack);
    return;
  }
  console.log('Connected to database as id ' + connection.threadId);

  // Process each entry in the JSON data
  jsonData.forEach((entry) => {
    const address = entry.address;
    const latlng = JSON.stringify(entry.latlng); // Convert the array to a JSON string

    // Check if the address matches in the 'inmuebles' table and update if it does
    const query = `UPDATE inmuebles SET coordinates = ? WHERE direccion = ?`;
    connection.query(query, [latlng, address], (error, results) => {
      if (error) {
        console.error(`Error updating address ${address}: `, error);
        return;
      }
      console.log(`Updated ${results.affectedRows} row(s) for address: ${address}`);
    });
  });

  // Close the connection
  connection.end((err) => {
    if (err) {
      console.error('Error closing the connection: ', err);
      return;
    }
    console.log('Connection closed.');
  });
});
