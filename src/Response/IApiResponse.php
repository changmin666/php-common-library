<?php
declare (strict_types=1);

namespace PXCommon\Response;


interface IApiResponse
{
    public static function handle(?object $data): mixed;
}