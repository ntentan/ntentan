<?php
namespace ntentan\sessions\stores;

interface Store
{
    public function open($sessionName, $sessionId);
    public function close();
    public function read($sessionId);
    public function write($sessionId, $data);
    public function destroy($sessionId);
    public function gc($lifetime);
    public function isNew();
}
