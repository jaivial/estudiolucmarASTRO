import mysql from 'mysql';
import fs from 'fs';

// MySQL database credentials
const host = '127.0.0.1';
const port = '3308'; // Make sure this matches your MySQL server port
const user = 'root';
const password = '';
const database = 'u212050690_estudiolucmar';

// Read and parse the JSON file
const jsonData = JSON.parse(fs.readFileSync('updated_bounding_boxes.json', 'utf8'));

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

  // Query to find entries with NULL coordinates
  const nullQuery = 'SELECT * FROM inmuebles WHERE coordinates IS NULL';
  connection.query(nullQuery, (error, results) => {
    if (error) {
      console.error('Error executing query: ', error);
      return;
    }

    console.log('Entries with NULL coordinates:', results);

    // Iterate through the query results
    results.forEach((row) => {
      const direccion = row.direccion;

      // Find the corresponding entry in the JSON data
      const jsonEntry = jsonData.find((entry) => entry.address === direccion);

      if (jsonEntry) {
        const boundingBox = JSON.stringify(jsonEntry.boundingBox); // Convert the array to a JSON string

        // Update the coordinates column with the boundingBox array
        const updateQuery = `UPDATE inmuebles SET coordinates = ? WHERE direccion = ?`;
        connection.query(updateQuery, [boundingBox, direccion], (updateError, updateResults) => {
          if (updateError) {
            console.error(`Error updating address ${direccion}: `, updateError);
            return;
          }
          console.log(`Updated ${updateResults.affectedRows} row(s) for address: ${direccion}`);
        });
      }
    });

    // Close the connection
    connection.end((endErr) => {
      if (endErr) {
        console.error('Error closing the connection: ', endErr);
        return;
      }
      console.log('Connection closed.');
    });
  });
});
