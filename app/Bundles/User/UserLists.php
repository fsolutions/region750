<?php
namespace App\Bundles\User;

use App\Models\User;
use Illuminate\Http\Request;

class UserLists
{

    public function userLists(Request $request)
    {
        $formData = $request->all();

        if (!isset($formData['list_type'])) {
            return [];
        }

        $excludeRoleList = [];

        switch ($formData['list_type']) {
            case 'for-accountants':
                //'administrator', 'client', 'accountant', 'head-accountant'
                $excludeRoleList = [1, 2, 9, 10];
                break;
            default:
                return [];
                break;
        }


        return User::whereHas('roles', function($query) use ($excludeRoleList){
            $query->whereNotIn('roles.id', $excludeRoleList);
        })->get(['users.id', 'users.name']);
    }

}
