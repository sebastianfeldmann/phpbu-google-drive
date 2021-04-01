# PHPBU Google Drive utility
With this CLI script you can create a Google Drive access token file that is
required by PHPBU to sync your files to Google Drive.

Also it lists the names and IDs of your Google Drive files and directories.
The ID of the directory where you want to store your backups is needed for your
PHPBU configuration.

All you need is a Google API client JSON credential file that you can easily
generate online in your Google account settings.

## Installation

There are two ways to install the PHPBU Google Drive utility.

### Composer

    composer require phpbu/google-drive-utility
    vendor/bin/phpbu-gdu
    
### git

    git clone https://github.com/sebastianfeldmann/phpbu-google-drive.git
    cd phpbu-google-drive
    ./phpbu-gdu
    
## Usage

    phpbu-gdu [client_secret.json] [client_access.json]
    
The first argument is the path to your local `client_secret.json` that you have
generated in your Google account settings.
If you do not provide the argument it defaults to `./client_secret.json`.

The second argument is the path where the access token should be stored.
If not provided it defaults to `./client_access.json`.
