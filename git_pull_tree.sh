#!/bin/bash
find . -maxdepth 1 -type d -exec git --git-dir={}/.git --work-tree=$PWD/{} pull origin master \;