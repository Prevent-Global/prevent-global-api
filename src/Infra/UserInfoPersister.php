<?php


namespace App\Infra;


use App\Entities\UserInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserInfoPersister
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
     * @param UserInfo $userInfo
     * @throws \Exception
     */
    public function saveUser(UserInfo $userInfo): void
    {
        try {

            mkdir($this->uploadDir . '/' . $userInfo->getId());
            $fp = fopen($this->uploadDir . '/' . $userInfo->getId() . "/userInfo.json","wb");
            fwrite($fp, json_encode($userInfo));
            fclose($fp);

        } catch (\Exception $e) {

            $this->logger->error("Error while uploading a UserInfo file", ['exception' => $e]);

            throw $e;
        }
    }
}