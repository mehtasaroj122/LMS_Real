@foreach($users as $user)
    @include('Admin.partials.user-row', ['user' => $user])
@endforeach
