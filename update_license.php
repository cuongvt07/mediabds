<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\User;
$u = User::where('phone', '0981847977')->first();
if($u) {
    $u->update(['license_key' => 'ZrFKwixJgykc']);
    echo "License updated for admin\n";
} else {
    echo "Admin not found\n";
}
