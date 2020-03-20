<?php


namespace App\Controller;

use App\Infra\KmlFileUploader;
use App\Infra\NotKmlFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class KmlUploadController
{
    const KML_FILE_FIELD_NAME = "locationHistory";

    /**
     * @Route("/api/kml/upload", name="kml_upload")
     */
    public function upload(Request $request, KmlFileUploader $kmlFileUploader)
    {
        try {
            $file = $request->files->get(self::KML_FILE_FIELD_NAME);
            $userId = $request->get("userId");

            if (!$file) {
                return new Response(null, Response::HTTP_BAD_REQUEST);
            }
            $kmlFileUploader->saveFile($userId, $file);
            return new Response(null, Response::HTTP_CREATED);

        } catch (NotKmlFileException $e) {
            return new Response(null, Response::HTTP_BAD_REQUEST);

        } catch (FileException $e) {
            return new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

}