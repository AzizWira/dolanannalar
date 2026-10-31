@extends('emails.layout')
@section('title', $heading)
@section('preheader', $intro)
@section('label', $label)
@section('heading', $heading)
@section('content')
<p style="margin:0 0 14px;">Halo {{ $name }},</p>
<p style="margin:0;">{{ $intro }}</p>
@endsection
@section('action', $action)
@section('note'){{ $note }} Tautan ini berlaku selama {{ $expires }} menit.@endsection
