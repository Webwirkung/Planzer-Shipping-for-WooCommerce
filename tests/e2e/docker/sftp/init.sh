#!/bin/bash
# atmoz/sftp chroots the user into a root-owned home, so the plugin cannot
# create "Eingang" itself. Create it writable for the user.
mkdir -p /home/WooCommerce/Eingang
chown 1001:100 /home/WooCommerce/Eingang
