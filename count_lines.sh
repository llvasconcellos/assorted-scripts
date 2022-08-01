#!/bin/bash
find . -type f -printf "%p\0" | wc -l --files0-from=-
