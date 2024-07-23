#!/bin/bash
git rm -r --cached .
git add dist/.
git status
git commit -m "Deploying"
git remote -v
git push dist-branch main




