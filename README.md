IpCamera cp
==============

This is a open source IpCamera control panel with FFMPEG and motion framework.

| Features |
|:---|
| Full responsive (smartphone, tablet, pc) |
| Cross-browser (Chrome, firefox, internet explorer, opera, safari) |
| Work on internet and lan (server, cloud, ...) |
| Work with a lot model of ipcamera |
| Remote camera control (Move and take picture) |
| Set motion tracking (Take automatic video) |
| Add and remove camera without write code |
| Check status |
| Apparatus profile management |
| Files management |
| Settings management |
| User profile management |
| Scss style |

## Images
<img src="screenshots/1.png" width="200" alt="1"/>
<img src="screenshots/2.png" width="200" alt="2"/>

## Instructions:
1) Download from "https://github.com/Motion-Project/motion/releases" the "xenial_motion_4.0.1-1_amd64.deb" package and upload this file on your server.

2) On linux, open terminal and write:

	sudo dpkg -i /YOUR_USER_PATH/xenial_motion_4.0.1-1_amd64.deb; sudo apt-get -f install
	
	sudo nano /etc/motion/motion.conf

3) Edit:

	daemon on
	
	process_id_file /var/run/motion/motion.pid
	
	log_level 4
	
	output_pictures off
	
	ffmpeg_output_movies off
	
	ffmpeg_video_codec mpeg4
	
	text_right %Y-%m-%d\n%T | %q
	
	snapshot_filename %Y-%m-%d_%H:%M:%S_snapshot
	
	picture_filename %Y-%m-%d_%H:%M:%S_%q
	
	movie_filename %Y-%m-%d_%H:%M:%S
	
	timelapse_filename %Y-%m-%d_timelapse
	
	stream_port 0
	
	webcontrol_port 32402

4) Save, close the file and on linux, open the terminal and write:

	sudo chmod 666 /etc/motion/motion.conf
	
	sudo nano /etc/default/motion

5) Edit:

	start_motion_daemon=yes

6) Save, close the file and on linux, open the terminal and write:
	
	sudo mkdir /YOUR_PATH/motion
	
	sudo chown -R www-data:motion /YOUR_PATH/motion
	
	sudo find /YOUR_PATH/motion -type d -exec chmod 775 {} \;
	
	sudo find /YOUR_PATH/motion -type f -exec chmod 664 {} \;
	
	sudo service motion restart

7) Go on your browser and write <b>"http://YOUR_IP/ipcamera_cp/web/index.php"</b>

<b>By CIMO - www.reinventsoftware.org</b>