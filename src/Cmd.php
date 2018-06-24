<?php
/**
 * This file is part of phpbu.
 *
 * (c) Sebastian Feldmann <sebastian@phpbu.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace phpbu\GDU;

use Google_Client;
use Google_Service_Drive;

/**
 * Class Cmd
 *
 * @package phpbu\GDU
 * @author  Sebastian Feldmann <sebastian@phpbu.de>
 * @link    https://github.com/sebastianfeldmann/phpbu-google-drive
 * @since   Class available since Release 0.9.0
 */
class Cmd
{
    const VERSION = '0.9.0';

    /**
     * Create google access file.
     *
     * @param array $args
     */
    public function run(array $args)
    {
        printf("phpbu-gdu %s by Sebastian Feldmann and contributors.\n\n", self::VERSION);
        try {
            $this->help($args);
            $secretFile  = $this->getSecretPath($args);
            $accessFile  = $this->getAccessPath($args);
            $apiClient   = $this->createClient($secretFile);
            $accessToken = $this->getAccessToken($accessFile, $apiClient);

            $apiClient->setAccessToken($accessToken);
            $this->listFiles($apiClient);

            printf("\nFind you credentials file at: %s\n", $accessFile);

        } catch (\Exception $e) {
            printf("%s\n", $e->getMessage());
            exit(1);
        }
        exit(0);
    }

    /**
     * Print help message.
     *
     * @param array $args
     */
    private function help(array $args)
    {
        $help = $args[1] ?? '';

        if (in_array($help, ['-h', '--help'])) {
            echo <<<EOT
Usage: phpbu-gdu [client_secret.json] [client_access.json]

  -h, --help    Print this usage information

EOT;
            exit(0);
        }
    }

    /**
     * Check cli arguments for secret json path.
     *
     * @param  array $args
     * @return string
     */
    private function getSecretPath(array $args)
    {
        $secret = $args[1] ?? 'client_secret.json';
        if (!file_exists($secret)) {
            throw new \RuntimeException('could not find authentication file: ' . $secret);
        }

        return $secret;
    }

    /**
     * Check cli arguments for access json path.
     *
     * @param  array $args
     * @return string
     */
    private function getAccessPath(array $args)
    {
        return $args[2] ?? 'client_access.json';
    }

    /**
     * Setup google api client.
     *
     * @param  string $secret
     * @return \Google_Client
     * @throws \Google_Exception
     */
    private function createClient(string $secret) : Google_Client
    {
        $client = new Google_Client();
        $client->setApplicationName('phpbu');
        $client->setScopes(Google_Service_Drive::DRIVE);
        $client->setAuthConfig($secret);
        $client->setAccessType('offline');

        return $client;
    }

    /**
     * Create or update the access file.
     *
     * @param  string         $accessFile
     * @param  \Google_Client $apiClient
     * @return array
     */
    private function getAccessToken(string $accessFile, Google_Client $apiClient) : array
    {
        // Load previously authorized credentials from a file.
        if (file_exists($accessFile)) {
            $accessToken = json_decode(file_get_contents($accessFile), true);
        } else {
            // Request authorization from the user.
            $authUrl = $apiClient->createAuthUrl();
            printf("Open the following link in your browser:\n\n%s\n\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an accessFile token.
            $accessToken = $apiClient->fetchAccessTokenWithAuthCode($authCode);

            // Store the credentials to disk.
            if (!file_exists(dirname($accessFile))) {
                mkdir(dirname($accessFile), 0700, true);
            }
            file_put_contents($accessFile, json_encode($accessToken));
        }
        return $accessToken;
    }

    /**
     * List the names and IDs for up to 50 files.
     *
     * @param \Google_Client $apiClient
     */
    private function listFiles(Google_Client $apiClient)
    {
        $service   = new Google_Service_Drive($apiClient);
        $results   = $service->files->listFiles(
            [
                'includeTeamDriveItems' => false,
                'pageSize'              => 50,
                'fields'                => 'nextPageToken, files(id, name, createdTime, size)',
                'spaces'                => 'drive',
                'q'                     => 'trashed = false AND visibility = \'limited\'',
            ]
        );

        if (count($results->getFiles()) == 0) {
            print "No files found.\n";
        } else {
            print "Files:\n";
            /** @var \Google_Service_Drive_DriveFile $file */
            foreach ($results->getFiles() as $file) {
                printf(
                    "%s (%s) - %s (%s bytes)\n",
                    $file->getName(),
                    $file->getId(),
                    $file->getCreatedTime(),
                    $file->getSize()
                );
            }
        }
    }

    /**
     * Main method, is called by the cli command.
     */
    public static function main()
    {
        $app = new static();
        $app->run($_SERVER['argv']);
    }
}
