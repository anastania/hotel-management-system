<?php

class User extends Model {
    protected static $table = 'users';
    protected static $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'email_verified',
        'email_verification_token',
        'is_admin'
    ];
    
    public function setPassword($password) {
        $this->attributes['password'] = Security::hashPassword($password);
    }
    
    public function verifyPassword($password) {
        return Security::verifyPassword($password, $this->attributes['password']);
    }
    
    public function getFullName() {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    public function sendVerificationEmail() {
        if (!$this->email_verification_token) {
            $this->email_verification_token = Security::generateRandomToken();
            $this->save();
        }
        
        return Mailer::sendVerificationEmail($this->email, $this->email_verification_token);
    }
    
    public function verify() {
        $this->email_verified = true;
        $this->email_verification_token = null;
        return $this->save();
    }
    
    public function isAdmin() {
        return (bool) $this->is_admin;
    }
    
    public function getReservations() {
        return Reservation::where('user_id = ?', [$this->id]);
    }
    
    public function getReviews() {
        return Review::where('user_id = ?', [$this->id]);
    }
}
