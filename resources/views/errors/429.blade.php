@extends('errors.layout')

@section('code', '429')
@section('title', __('Please slow down'))
@section('message', __('Kirada received too many requests in a short time. Wait a moment, then try again.'))
