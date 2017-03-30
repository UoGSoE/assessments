<?php

namespace App;

trait CanBeCreatedFromOutsideSources
{
    public static function createFromLdap($ldapData)
    {
        $user = new static([
            'username' => $ldapData['username'],
            'surname' => $ldapData['surname'],
            'forenames' => $ldapData['forenames'],
            'email' => $ldapData['email'],
            'password' => bcrypt(str_random(64))
        ]);
        $user->is_student = $user->usernameIsMatric($ldapData['username']);
        $user->save();
        return $user;
    }

    public static function staffFromWlmData($wlmStaff)
    {
        $wlmStaff['Username'] = $wlmStaff['GUID'];
        return static::userFromWlmData($wlmStaff, false);
    }

    public static function studentFromWlmData($wlmStudent)
    {
        $wlmStudent['Username'] = strtolower($wlmStudent['Matric'] . substr($wlmStudent['Surname'], 0, 1));
        $wlmStudent['Email'] = $wlmStudent['Username'] . "@student.gla.ac.uk";
        return static::userFromWlmData($wlmStudent, true);
    }

    protected static function userFromWlmData($wlmData, $isStudent = false)
    {
        $user = User::findByUsername($wlmData['Username']);
        if (!$user) {
            $user = new static([
                'username' => $wlmData['Username'],
                'email' => $wlmData['Email'],
            ]);
        }
        $user->surname = $wlmData['Surname'] ?? 'Unknown';
        $user->forenames = $wlmData['Forenames'] ?? 'Unknown';
        $user->password = bcrypt(str_random(32));
        $user->is_student = $isStudent;
        $user->save();
        return $user;
    }
}
