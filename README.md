IpCamera cp
==============

Framework for management your IpCamera.
Based on Uebusaito, you can record video and/or image, setting your IpCamera and use a motion detection.

| INFO |
|:---|
| Cross browser and responsive (Chrome, Firefox, Edge, Opera, Safari) |
| Cross platform (Windows, Linux, Mac, Android, Ios ) |
| Dynamic multi language |
| Login, registration, recover password and profile |
| Multiple roles system |
| Search in website |
| Credit and paypal payment |
| Upload file chunk system |
| Wysiwyg page creation (create page without code) |
| Page comments |
| Microservice (Deploy and api) |
| Integration with: Slack, line |
| Extend with module system |

| ELEMENTS |
|:---|
| System info |
| Payments |
| Pages |
| Users |
| Modules |
| Roles |
| Settings |
| Slack |
| Line |
| Microservice |

| IMAGES |
|:---|
<img src="screenshots/1.png" width="200" alt="1.png"/>
<img src="screenshots/2.png" width="200" alt="2.png"/>

## Instructions:
1) Copy files on your server.

2) Write on terminal:

        cd /home/user_1/www/symfony_fw
        
        sudo nano .env

3) Modify:

        APP_ENV=dev
        
        DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name

4) Save, close the file and write on terminal:

        sudo nano /config/packages/framework.yaml

5) In "session:" modify:

        save_path: '%kernel.project_dir%/var/sessions/%kernel.environment%'
        
        name: new_name
        
        cookie_domain: .domain_name.xxx

6) Save, close the file and write on terminal:

        sudo cp /src/Config.php.dist /src/Config.php
        
        sudo nano /src/Config.php

7) Change variables for adapt the framework to the your system.

8) Save, close the file and write on terminal:

        sudo rm -rf vendor var/cache composer.lock
        
        sudo composer install
        
        sudo composer update
        
        sudo chmod 775 /home/user_1/www/symfony_fw
        
        sudo chown -R user_1:www-data /home/user_1/www/symfony_fw
        
        sudo find /home/user_1/www/symfony_fw -type d -exec chmod 775 {} \;
        
        sudo find /home/user_1/www/symfony_fw -type f -exec chmod 664 {} \;
        
        sudo -u www-data php bin/console cache:clear --no-warmup --env=dev

7) For admin login use <b>"cimo, Password1"</b>.

| LIBRARY |
|:---|
FFmpeg https://www.ffmpeg.org/

<b>By CIMO - https://www.reinventsoftware.org</b>
