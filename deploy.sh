#!/bin/bash
set -e

# Work in the root repository
echo "Working in the root repository"
cd .

# Change to the 'dist' directory
echo "Changing to 'dist' directory"
cd dist
git add ../dist -f
git status


