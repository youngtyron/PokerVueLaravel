@extends('layouts.app')

@section('content')
  <desk-component :match="{{$match_id}}" :gamer="{{$gamer_id}}"></desk-component>
@endsection
