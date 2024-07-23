#!/bin/bash
git rm -r --cached .
# Change directory to 'dist'
cd ./dist

# Add all changes to the staging area
git add . -f

# Commit changes with a message
git commit -m "deployment build"

# Push changes to the remote 'dist-branch'
git push dist-branch main

# Return to the previous directory
cd ..
