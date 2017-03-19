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

    public function containerInfo()
    {
        
    }

    public function containerList()
    {
        return $this->api->makePrivateRequest('get', ['format' => 'json']);
    }

    public function createContainer()
    {
        
    }

    public function changeContainerInfo()
    {
        
    }

    public function deleteContainer()
    {
        
    }

    public function gallery()
    {
        
    }

    /**
     * @param string $container
     * @return array
     */
    public function fileList($container)
    {
        return $this->api->makePrivateRequest('get', ['format' => 'json'], [], $container);
    }

    /**
     * Check if container is public
     */
    public function getFile()
    {
        
    }

    public function storeFile()
    {
        
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