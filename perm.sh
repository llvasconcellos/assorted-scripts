#!/bin/bash
sudo chown -R leonardo:www-data gabivel-wp
sudo find gabivel-wp -type d -exec chmod 775 {} \;
sudo find gabivel-wp -type f -exec chmod 664 {} \;