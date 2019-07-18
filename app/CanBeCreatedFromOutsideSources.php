<?php

namespace App;

use Illuminate\Support\Str;

trait CanBeCreatedFromOutsideSources
{
    /**
     * Create a user record based on data from LDAP
     */
    public static function createFromLdap($ldapData)
    {
        $user = new static([
            'username' => $ldapData['username'],
            'surname' => $ldapData['surname'],
            'forenames' => $ldapData['forenames'],
            'email' => $ldapData['email'],
            'password' => bcrypt(Str::random(64))
        ]);
        $user->is_student = $user->usernameIsMatric($ldapData['username']);
        $user->save();
        return $user;
    }

    /**
     * Create a staff record based on data from the Workload Model
     */
    public static function staffFromWlmData($wlmStaff)
    {
        $wlmStaff['Username'] = $wlmStaff['GUID'];
        return static::userFromWlmData($wlmStaff, false);
    }

    /**
     * Create a student record based on data from the Workload Model
     */
    public static function studentFromWlmData($wlmStudent)
    {
        $wlmStudent['Username'] = strtolower($wlmStudent['Matric'] . substr($wlmStudent['Surname'], 0, 1));
        $wlmStudent['Email'] = $wlmStudent['Username'] . "@student.gla.ac.uk";
        return static::userFromWlmData($wlmStudent, true);
    }

    /**
     * Create a user record based on WLM data
     */
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
        $user->password = bcrypt(Str::random(32));
        $user->is_student = $isStudent;
        $user->save();
        return $user;
    }
}
