# wordpress-audio-upload-trigger
Calls a webhook when an audio file is uploaded to a Wordpress site's media library

## Installation
Download and compress n8n_upload_trigger.php, then under your-wordpress-site.com/wp-admin select Plugins > Add Plugin.  Click Upload Plugin, select the zip file and install. When the installation is finished, click Activate

## Use
On the admin dashboard, go to Settings > Audio Upload to n8n.  Enter the webhook url in the box and click save.  Now whenever an audio file is uploaded to the media library, the site will send a POST request containing the url of the audio file
