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
     * Create a staff record based on data from the Teaching Office DB
     */
    public static function staffFromTODBData($todbStaff)
    {
        $todbStaff['Username'] = $todbStaff['GUID'];
        return static::userFromTODBData($todbStaff, false);
    }

    /**
     * Create a student record based on data from the Teaching Office DB
     */
    public static function studentFromTODB($todbStudent)
    {
        $todbStudent['Username'] = strtolower($todbStudent['Matric'] . substr($todbStudent['Surname'], 0, 1));
        $todbStudent['Email'] = $todbStudent['Username'] . "@student.gla.ac.uk";
        return static::userFromTODB($todbStudent, true);
    }

    /**
     * Create a user record based on Teaching Office data
     */
    protected static function userFromTODB($todbUser, $isStudent = false)
    {
        $user = User::findByUsername($todbUser['Username']);
        if (!$user) {
            $user = new static([
                'username' => $todbUser['Username'],
                'email' => $todbUser['Email'],
            ]);
        }
        $user->surname = $todbUser['Surname'] ?? 'Unknown';
        $user->forenames = $todbUser['Forenames'] ?? 'Unknown';
        $user->password = bcrypt(Str::random(32));
        $user->is_student = $isStudent;
        $user->save();
        return $user;
    }
}
