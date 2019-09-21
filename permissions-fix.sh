sudo chgrp -R www-data /var/www/axiom
sudo find /var/www/axiom -type d -exec chmod g+rx {} +
sudo find /var/www/axiom -type f -exec chmod g+r {} +
sudo chown -R www-data /var/www/axiom/
sudo find /var/www/axiom -type d -exec chmod u+rwx {} +
sudo find /var/www/axiom -type f -exec chmod u+rw {} +
sudo find /var/www/axiom -type d -exec chmod g+s {} +
sudo chmod -R o-rwx /var/www/axiom/
