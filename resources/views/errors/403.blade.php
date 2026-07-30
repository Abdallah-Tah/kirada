@extends('errors.layout')

@section('code', '403')
@section('title', __('This action is not available'))
@section('message', __($exception->getMessage() ?: 'You do not have permission to access this page or complete this action.'))
