#!/bin/bash

gitCloneUrl=https://username:password@github.com/cimo/project_name.git
#gitCloneUrl=https://oauth2:token@github.com/cimo/project_name.git
gitClonePath=/home/user_1/www/project_folder
userGitScript=user_1
userWebScript=user_1:www-data
rootWebPath=/home/user_1/www/project_folder
success=0

sudo git config --global core.mergeoptions --no-edit
sudo git config --global user.email "email"
sudo git config --global user.name "username"

echo "Git shell"
read -p "1) Clone - 2) Pull: - 3) Reset: > " gitChoice

if [ ! -z $gitChoice ]
then
    if [ $gitChoice -eq 1 ]
    then
        sudo -u $userGitScript git clone $gitCloneUrl $gitClonePath
        
        success=1
    elif [ $gitChoice -eq 2 ]
    then
        read -p "Insert branch name: > " branchNameA branchNameB

        if [ ! -z "$branchNameA $branchNameB" -a "$branchNameA $branchNameB" != " " ]
        then
                cd $gitClonePath
                sudo -u $userGitScript git pull --no-edit $gitCloneUrl $branchNameA $branchNameB
                
                success=1
        else
                echo "Empty value, please restart!"
        fi
    elif [ $gitChoice -eq 3 ]
    then
        cd $gitClonePath
        sudo -u $userGitScript git fetch --all
        sudo -u $userGitScript git reset --hard
        
        success=1
    fi
    
    if [ $success -eq 1 ]
    then
        cd $rootWebPath/symfony_fw
        sudo -u www-data php bin/console cache:clear --no-warmup --env=dev

        echo "Settings project in progress, please wait..."

        sudo chown -R $userWebScript $rootWebPath
        sudo find $rootWebPath -type d -exec chmod 775 {} \;
        sudo find $rootWebPath -type f -exec chmod 664 {} \;

        echo "Finito ciao ciao =D"
    fi
else
    echo "Empty value, please restart!"
fi