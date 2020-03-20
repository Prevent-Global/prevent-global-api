<?php


namespace App\Controller;

use App\Infra\KmlFileUploader;
use App\Infra\NotKmlFileException;
use App\Infra\UserNotRegistered;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class KmlUploadController
{
    const KML_FILE_FIELD_NAME = "locationHistory";

    /**
     * @Route("/api/kml/upload-form", name="kml_upload_form")
     */
    public function uploadForm(Request $request, KmlFileUploader $kmlFileUploader)
    {
        try {
            $file = $request->files->get(self::KML_FILE_FIELD_NAME);
            $userId = $request->get("userId");

            if (!$file) {
                return new Response(json_encode(["error" => "Expected KML file missing"]), Response::HTTP_BAD_REQUEST);
            }

            $kmlFileUploader->saveFile($userId, $file);
            return new Response(null, Response::HTTP_CREATED);

        } catch (NotKmlFileException $e) {
            return new Response(json_encode(["error" => "File is not a valid KML file"]), Response::HTTP_BAD_REQUEST);

        } catch (FileException $e) {
            return new Response(json_encode(["error" => "Error occurred while saving the file"]), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    /**
     * @Route("/api/kml/upload", name="kml_upload")
     */
    public function upload(Request $request, KmlFileUploader $kmlFileUploader)
    {
        try {

            $userId = $request->headers->get("X-UserId");
            $fileContent = $request->getContent();

            if (!$fileContent) {
                return new Response(json_encode(["error" => "Expected KML file missing"]), Response::HTTP_BAD_REQUEST);
            }
            $kmlFileUploader->saveString($userId, $fileContent);
            return new Response(null, Response::HTTP_CREATED);

        } catch (NotKmlFileException $e) {
            return new Response(json_encode(["error" => "File is not a valid KML file"]), Response::HTTP_BAD_REQUEST);

        } catch (UserNotRegistered $e) {
            return new Response(json_encode(["error" => "Specified user was not registered"]), Response::HTTP_BAD_REQUEST);
        }
    }

}