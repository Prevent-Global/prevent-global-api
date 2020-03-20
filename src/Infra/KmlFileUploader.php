<?php


namespace App\Infra;


use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class KmlFileUploader
{
    const KML_FILES_DIRECTORY_PARAM = 'kml_files_directory';
    const KML_FILE_EXTENSION = "kml";
    const GUESSED_EXTENSION = "xml";
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $uploadDir;

    public function __construct(LoggerInterface $logger, ContainerBagInterface $params)
    {
        $this->logger = $logger;
        $this->uploadDir = $params->get(self::KML_FILES_DIRECTORY_PARAM);
    }

    /**
     * @param string $userId
     * @throws UserNotRegistered
     */
    private function ValidateUserExists(string $userId): void
    {
        $fileSystem = new Filesystem();

        if (!$fileSystem->exists($this->uploadDir . '/' . $userId . '/' . 'userInfo.json')) {
            throw new UserNotRegistered("UserInfo file is missing");
        };
    }

    /**
     * @param UploadedFile $file
     * @throws NotKmlFileException
     * @throws FileException
     * @throws UserNotRegistered
     */
    public function saveFile(string $userId, UploadedFile $file): void
    {
        $this->ValidateUserExists($userId);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . self::KML_FILE_EXTENSION;

        if ($file->guessExtension() !== self::GUESSED_EXTENSION) {
            throw new NotKmlFileException();
        }

        // Move the file to the directory where kml files are stored
        try {
            $file->move(
                $this->uploadDir . '/' . $userId . '/',
                $newFilename
            );

        } catch (FileException $e) {

            $this->logger->error("Error while uploading a KML file", ['exception' => $e]);

            throw $e;
        }
    }
}