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

    public function storageInfo()
    {
        
    }

    public function containerInfo()
    {
        
    }

    public function containerList()
    {

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

    public function fileList()
    {
        
    }

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