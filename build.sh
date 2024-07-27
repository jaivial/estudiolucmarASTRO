#!/bin/bash

# Function to process files
process_files() {
    local dir=$1

    # Find all files in the directory and its subdirectories
    find "$dir" -type f | while read -r file; do
        echo "Checking file: $file"
        # Check if the file contains the string we are looking for
        if grep -q 'http://localhost:8000/' "$file"; then
            echo "Found matching string in: $file"
            # Replace the string in the file
            sed -i '' 's|http://localhost:8000/|https://estudiolucmar.com/|g' "$file"
            if [ $? -eq 0 ]; then
                echo "Processed: $file"
            else
                echo "Failed to process: $file"
            fi
        fi
    done
}

# Function to update database connection details
update_db_connection() {
    local db_file="./public/backend/db_Connection/db_Connection.php"

    if [ -f "$db_file" ]; then
        echo "Updating database connection details in $db_file"
        sed -i '' 's|\$host = "127.0.0.1";|\$host = "localhost";|g' "$db_file"
        sed -i '' 's|\$port = "3308";|\$port = "3306";|g' "$db_file"
        sed -i '' 's|\$user = "root";|\$user = "u212050690_estudiolucmar";|g' "$db_file"
        sed -i '' 's|\$password = "";|\$password = "estudioLucmar_4321";|g' "$db_file"
        sed -i '' 's|\$database = "u212050690_estudiolucmar";|\$database = "u212050690_estudiolucmar";|g' "$db_file"
        echo "Database connection details updated in $db_file"
    else
        echo "Database connection file $db_file does not exist."
    fi
}

# Check if the ./src directory exists
if [ -d "./src" ]; then
    echo "Starting processing in ./src"
    # Start processing from the ./src directory
    process_files "./src"
    echo "All matching strings have been processed."
else
    echo "Directory ./src does not exist."
fi

# Update the database connection details
update_db_connection
