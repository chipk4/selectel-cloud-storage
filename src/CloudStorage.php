<?php namespace Chipk4\Selectel;

class CloudStorage
{

    /**
     * @var Api
     */
    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @return array|false
     */
    public function storageInfo()
    {
        return $this->api->makePrivateRequest('head');
    }

    /**
     * @param string $name Container name
     * @return array|false
     */
    public function containerInfo($name)
    {
        return $this->api->makePrivateRequest('head', [], [], $name);
    }

    public function storageContainerList()
    {
        return $this->api->makePrivateRequest('get', [
            'format' => $this->api->getReturnView()
        ]);
    }

    /**
     * @param string $name This is a container name
     * @param string $visible Can be, public, private, gallery
     * @return array|false
     */
    public function createContainer($name, $visible = 'public')
    {
//        return $this->api->makePrivateRequest(
//            'put',
//            [],
//            [Api::HEADER_CONTAINER_TYPE.': '.$visible],
//            $name
//        );
    }

    public function changeContainerInfo()
    {
        
    }

    public function deleteContainer($name)
    {

    }

    public function gallery()
    {
        
    }

    /**
     * @param string $name
     * @return array
     */
    public function containerFileList($name)
    {
        return $this->api->makePrivateRequest('get', ['format' => $this->api->getReturnView()], [], $name);
    }

    /**
     * Check if container is public
     *
     * @param string $container
     * @param string $file
     * @param boolean $privateContainer
     * @return array|false
     */
    public function getFile($container, $file, $privateContainer=false)
    {
        if(!$privateContainer) {
            return $this->api->makePublicRequest('get', [], [], $container.'/'.$file);
        }
        return $this->api->makePrivateRequest('get', [], [], $container.'/'.$file);
    }

    public function storeFile($container, $filePath, $fileName)
    {
        return $this->api->makePrivateRequest(
            'put',
            ['file' => $filePath],
            ['Content-Length: ' . filesize($filePath)],
            $container.'/'.$fileName
        );
    }

    public function unpackArchive($isInBackground=false)
    {

    }

    public function changeFileInfo()
    {

    }

    public function copyFile()
    {

    }

    public function deleteFile()
    {
        
    }

    public function getApi()
    {
        return $this->api;
    }

}