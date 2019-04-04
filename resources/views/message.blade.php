@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header">Encrypt</div>

                <div class="card-body">

                    <form method="POST" action="/encrypt">
                        @csrf
                        <div class="form-group">
                            <label for="message">Message</label>
                            <input type="message" class="form-control" name="message" id="message"/>
                        </div>

                        <button type="submit" class="btn btn-primary">Encrypt message</button>
                    </form>

                    @isset($messageEncrypted)
                    <p>
                        Encrypted string:
                    </p>
                    <p>
                        {{ $messageEncrypted }}
                    </p>
                    @endisset
                </div>
            </div>
            <br/>

            <div class="card">
                <div class="card-header">Decrypt</div>

                <div class="card-body">

                    <form method="POST" action="/decrypt">
                        @csrf
                        <button type="submit" class="btn btn-primary">Decrypt message</button>
                    </form>

                    @isset($message)
                    <p>
                        {{ $message }}
                    </p> 
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
