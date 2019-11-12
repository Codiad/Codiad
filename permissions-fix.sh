ATHEOS="/var/www/atheos/"
sudo chgrp -R www-data $ATHEOS
sudo find $ATHEOS -type d -exec chmod g+rx {} +
sudo find $ATHEOS -type f -exec chmod g+r {} +
sudo chown -R www-data $ATHEOS
sudo find $ATHEOS -type d -exec chmod u+rwx {} +
sudo find $ATHEOS -type f -exec chmod u+rw {} +
sudo find $ATHEOS -type d -exec chmod g+s {} +
sudo chmod -R o-rwx $ATHEOS
