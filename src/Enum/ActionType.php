<?php

namespace App\Enum;

enum ActionType: string
{
    case ADD = 'add';
    case EDIT = 'edit';
    case DELETE = 'delete';
}
