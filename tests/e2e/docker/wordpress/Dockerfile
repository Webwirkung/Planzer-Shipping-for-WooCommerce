FROM wordpress:php8.2-apache

# poppler-utils + zbar-tools: PDF checks (pdfinfo, pdftotext, pdftoppm, zbarimg).
RUN apt-get update \
 && apt-get install -y --no-install-recommends poppler-utils zbar-tools mariadb-client less \
 && rm -rf /var/lib/apt/lists/* \
 && curl -fsSL -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
 && chmod +x /usr/local/bin/wp
