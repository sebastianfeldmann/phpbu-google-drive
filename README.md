# PHPBU Google Drive utility
With this cli script you can create a Google Drive access token
file that is required by PHPBU to sync your files to Google Drive.

All you need is a Google api client json credential file that you can easily
generate online in you Google Drive account settings.

## Installation

There are two ways to install the phpbu Google Drive utility.

### Composer

    composer require phpbu/google-drive-util
    vendor/bin/phpbu-gdu
    
### git

    git clone https://github.com/sebastianfeldmann/phpbu-google-drive.git
    cd phpbu-google-drive
    ./phpbu-gdu
    
## Usage

    phpbu-gdu [client_secret.json] [client_access.json]
    
The first argument is the path to you local client_secret.json that you got from
your Google Drive account settings.
If not provided it defaults to `./client_secret.json`.

The second argument is the path where the access token should be stored.
If not provided it defaults to `./client_access.json`.
