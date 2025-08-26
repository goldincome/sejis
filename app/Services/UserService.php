<?php
namespace App\Services;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserService
{

    public function generateCustomerNumber()
    {
        $number = mt_rand(1000000000, 9999999999); // better than rand()

        // call the same function if the number exists already
        if ($this->customerNumberExists($number)) {
            return $this->generateCustomerNumber();
        }
        // otherwise, it's valid and can be used
        return $number;
    }

    protected function customerNumberExists($number)
    {
        return User::where('customer_no', $number)->exists();
    }

    public function getAllCustomers()
    {
        return User::where('user_type', UserTypeEnum::CUSTOMER->value)->latest()->paginate(10);
    }

    public function getAllAdmins()
    {
        return User::where('user_type', UserTypeEnum::ADMIN->value)->latest()->paginate(10);
    }
    /**
     * Create a new user.
     *
     * @param array $data
     * @return \App\Models\User
     */
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Update an existing user.
     *
     * @param \App\Models\User $user
     * @param array $data
     * @return \App\Models\User
     */
    public function updateUser(User $user, array $data): User
    {
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        return $user;
    }

    /**
     * Delete a user.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
    }

}