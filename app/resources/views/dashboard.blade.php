@extends('layouts.app')

@section('title', 'Dashboard - Portal de Aplicativos')

@push('styles')
    <style>
        .small-box .small-box-icon {
            position: absolute;
            right: 15px;
            top: 15px;
            z-index: 0;
            font-size: 70px;
            color: rgba(0, 0, 0, 0.15);
            transition: transform 0.3s linear;
        }

        .small-box:hover .small-box-icon {
            transform: scale(1.1);
        }

        .small-box .inner {
            z-index: 10;
            position: relative;
        }
    </style>
@endpush

@section('content')
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box text-bg-primary shadow-sm h-100">
                <div class="inner">
                    <h3>{{ $totalEventos ?? 0 }}</h3>
                    <p>Total de Eventos</p>
                </div>
                <div class="small-box-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <a href="{{ route('eventos.index') }}"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-100-hover">
                    Mais informações <i class="fa-solid fa-circle-arrow-right"></i>
                </a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box text-bg-success shadow-sm h-100">
                <div class="inner">
                    <h3>{{ $totalConvites ?? 0 }}</h3>
                    <p>Convites Emitidos</p>
                </div>
                <div class="small-box-icon">
                    <i class="fa-solid fa-ticket text-white-50"></i>
                </div>
                <a href="{{ route('eventos.index') }}"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-100-hover">
                    Mais informações <i class="fa-solid fa-circle-arrow-right"></i>
                </a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box text-bg-warning shadow-sm h-100">
                <div class="inner">
                    <h3>{{ $totalConvidados ?? 0 }}</h3>
                    <p>Total Convidados</p>
                </div>
                <div class="small-box-icon">
                    <i class="fa-solid fa-user-group text-dark-50"></i>
                </div>
                <a href="{{ route('eventos.index') }}"
                    class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-100-hover">
                    Mais informações <i class="fa-solid fa-circle-arrow-right"></i>
                </a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box text-bg-danger shadow-sm h-100">
                <div class="inner">
                    <h3>R$ {{ number_format($totalArrecadado ?? 0, 2, ',', '.') }}</h3>
                    <p>Arrecadação Geral</p>
                </div>
                <div class="small-box-icon">
                    <i class="fa-solid fa-hand-holding-dollar text-white-50"></i>
                </div>
                <a href="{{ route('eventos.index') }}"
                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-100-hover">
                    Mais informações <i class="fa-solid fa-circle-arrow-right"></i>
                </a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- /.row -->

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title m-0"><i class="fa-solid fa-list me-2"></i> Eventos Recentes</h3>
                    <div class="card-tools ms-auto">
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Evento</th>
                                    <th>Data</th>
                                    <th>Convidados</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentes ?? [] as $evento)
                                    <tr>
                                        <td><strong>{{ $evento->nome }}</strong></td>
                                        <td>{{ $evento->data_inicio?->format('d/m/Y') ?? 'A definir' }}</td>
                                        <td>{{ $evento->convidados_count ?? 0 }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('eventos.index') }}"
                                                class="btn btn-sm btn-outline-primary">Ver</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Nenhum evento recente encontrado
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('eventos.index') }}" class="small text-uppercase fw-bold text-decoration-none">Ver
                        todos os eventos</a>
                </div>
            </div>
        </div>
    </div>
@endsection