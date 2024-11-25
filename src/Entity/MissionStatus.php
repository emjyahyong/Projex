<?php
namespace App\Entity;

enum MissionStatus:string
{
    case STATUS_PENDING = 'PENDING';
    case STATUS_IN_PROGRESS = 'IN_PROGRESS';
    case STATUS_COMPLETED = 'COMPLETED';
    case STATUS_FAILED = 'FAILED';
}
?>