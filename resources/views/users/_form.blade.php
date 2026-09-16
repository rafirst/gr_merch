<div class="form-row">
    <div class="form-group col-md-6">
        <label>Nama</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Username</label>
        <input type="text" name="username" class="form-control" value="{{ old('username', $user->username ?? '') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '' }}</label>
        <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
    </div>
    <div class="form-group col-md-6">
        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }}>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Role</label>
        <select name="role" class="form-control" required>
            <option value="staff_cabang" {{ old('role', $user->role ?? '') == 'staff_cabang' ? 'selected' : '' }}>Staff Cabang</option>
            <option value="admin_ho" {{ old('role', $user->role ?? '') == 'admin_ho' ? 'selected' : '' }}>Admin HO</option>
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Cabang</label>
        <select name="cabang_id" class="form-control" required>
            @foreach($cabangs as $c)
                <option value="{{ $c->id }}" {{ old('cabang_id', $user->cabang_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
            @endforeach
        </select>
    </div>
</div>
