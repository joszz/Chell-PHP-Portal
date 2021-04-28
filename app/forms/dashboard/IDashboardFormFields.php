<?php

namespace Chell\Forms\Dashboard;

interface IDashboardFormFields
{
    function setFields($form);
    function setPostData(&$config, $data);
}