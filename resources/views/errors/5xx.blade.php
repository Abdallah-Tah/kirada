@extends('errors.layout')

@section('code', $exception->getStatusCode())
@section('title', __('Kirada encountered a problem'))
@section('message', __('Your data is safe. Please return to your workspace and try again in a moment.'))
