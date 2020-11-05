<?php

namespace TaskForce\Actions;

use TaskForce\Constant\UserRole;
use TaskForce\TaskEntity;

class FailedAction extends AbstractAction
{
    private const TITLE = 'Failed';

    public static function getTitle(): string
    {
        return self::TITLE;
    }

    public static function getName(): string
    {
        return self::class;
    }

    public static function isAllowed(bool $isOwner, string $status, string $role): bool
    {
        return ($isOwner && $role === UserRole::CUSTOMER && $status === TaskEntity::STATUS_IN_WORK);
    }

}