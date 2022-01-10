@if ($message = Session::get('success'))
  <div class="bg-green-600 rounded-md my-4 text-white text-sm font-bold px-4 py-3" role="alert">
    <p>{{ $message }}</p>
  </div>
@endif
@if ($message = Session::get('error'))
  <div class="bg-red-600 rounded-md my-4 text-white text-sm font-bold px-4 py-3" role="alert">
    <p>{{ $message }}</p>
  </div>
@endif
@if ($message = Session::get('warning'))
  <div class="bg-yellow-600 rounded-md my-4 text-white text-sm font-bold px-4 py-3" role="alert">
    <p>{{ $message }}</p>
  </div>
@endif
@if ($message = Session::get('info'))
  <div class="bg-blue-600 rounded-md my-4 text-white text-sm font-bold px-4 py-3" role="alert">
    <p>{{ $message }}</p>
  </div>
@endif
@if ($errors->any())
  @foreach ($errors->all() as $error)
    <div class="bg-red-600 rounded-md my-4 text-white text-sm font-bold px-4 py-3" role="alert">
      <p>{{ $error }}</p>
    </div>
  @endforeach
@endif
