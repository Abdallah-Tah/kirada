@extends('errors.layout')

@section('code', $exception->getStatusCode())
@section('title', __('We could not complete that request'))
@section('message', __($exception->getMessage() ?: 'Check the request and try again, or return to your Kirada workspace.'))
