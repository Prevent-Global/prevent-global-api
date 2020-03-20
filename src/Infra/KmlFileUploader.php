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
    /**
     * @var string
     */
    private $queueFile;

    public function __construct(LoggerInterface $logger, ContainerBagInterface $params)
    {
        $this->logger = $logger;
        $this->uploadDir = $params->get(self::KML_FILES_DIRECTORY_PARAM);
        $this->queueFile = $params->get(self::KML_FILES_DIRECTORY_PARAM) . "/uploadedFiles.csv";
    }

    /**
     * @param string $userId
     * @param string $fileContent
     * @throws NotKmlFileException
     * @throws UserNotRegistered
     */
    public function saveString(string $userId, string $fileContent): void
    {
        $this->ValidateUserExists($userId);

        $xmlDocument = new \XMLReader();
        $xmlDocument->XML($fileContent);
        $xmlDocument->setParserProperty(\XMLReader::VALIDATE, true);

        if(!$xmlDocument->isValid()) {
            throw new NotKmlFileException();
        }

        $fileName = uniqid() . '.' . self::KML_FILE_EXTENSION;

        // Move the file to the directory where kml files are stored
        try {
            $uploadDir = $this->uploadDir . '/' . $userId . '/';

            $fp = fopen($uploadDir . $fileName,"wb");
            fwrite($fp, $fileContent);
            fclose($fp);

            $this->logger->info("KML File uploaded");
            $this->raiseFileUploadedEvent($uploadDir, $fileName);

        } catch (\Exception $e) {

            $this->logger->error("Error while uploading a KML file", ['exception' => $e]);

            throw $e;
        }
    }
    /**
     * @param string $userId
     * @param UploadedFile $file
     * @throws NotKmlFileException
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
            $uploadDir = $this->uploadDir . '/' . $userId . '/';
            $file->move($uploadDir, $newFilename);

            $this->logger->info("KML File uploaded");
            $this->raiseFileUploadedEvent($uploadDir, $newFilename);

        } catch (FileException $e) {

            $this->logger->error("Error while uploading a KML file", ['exception' => $e]);

            throw $e;
        }
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

    private function raiseFileUploadedEvent(string $uploadDir, string $filename)
    {
        $fs = new Filesystem();

        if (!$fs->exists($this->queueFile)) {
            $fs->appendToFile($this->queueFile, "timestamp,path". PHP_EOL);
        }

        $now = new \DateTime('now');
        $fs->appendToFile($this->queueFile, sprintf("%s,%s/%s" . PHP_EOL, $now->getTimestamp(), $uploadDir, $filename));
    }
}