<?php

/**
 * @return array
 * @throws Exception
 */
function getUsersArray() {
    if (empty(getenv('USERS_TO_COMPARE'))) {
        throw new \Exception('users to compare are not set!!!');
    }

    $users = [];

    $userIdSets = explode(',', getenv('USERS_TO_COMPARE'));
    foreach ($userIdSets as $userIdSet) {
        $userIdSetPieces = explode(':', $userIdSet);

        if (!empty($userIdSetPieces[0]) && !empty($userIdSetPieces[1])) {
            $users[$userIdSetPieces[0]] = $userIdSetPieces[1];
        }
    }

    if (empty($users)) {
        throw new \Exception('users where not set in the correct format!!!');
    }

    return $users;
};