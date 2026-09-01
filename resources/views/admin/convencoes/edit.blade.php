@extends('layouts.app')

@section('title', 'Editar Convenção Coletiva')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-sm-6">
            <h1 class="h4 mb-0 text-gray-800 fw-bold">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Editar Convenção Coletiva
            </h1>
            <p class="text-muted small mb-0">{{ $convencao->titulo }}</p>
        </div>
        <div class="col-sm-6 text-end">
            <a href="{{ route('admin.convencoes.show', $convencao) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Ver Detalhes
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="m-0 fw-bold text-primary">Alterar Dados da Convenção</h6>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.convencoes.update', $convencao) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-secondary">Título da Convenção <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" 
                                    value="{{ old('titulo', $convencao->titulo) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Categoria Representada <span class="text-danger">*</span></label>
                                <select name="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
                                    <option value="QUIMICA" {{ old('categoria', $convencao->categoria) === 'QUIMICA' ? 'selected' : '' }}>Química (Dissídio Nov)</option>
                                    <option value="FARMACEUTICA" {{ old('categoria', $convencao->categoria) === 'FARMACEUTICA' ? 'selected' : '' }}>Farmacêutica (Dissídio Abr)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Início da Vigência <span class="text-danger">*</span></label>
                                <input type="date" name="vigencia_inicio" class="form-control @error('vigencia_inicio') is-invalid @enderror" 
                                    value="{{ old('vigencia_inicio', $convencao->vigencia_inicio ? $convencao->vigencia_inicio->format('Y-m-d') : '') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Término da Vigência <span class="text-danger">*</span></label>
                                <input type="date" name="vigencia_fim" class="form-control @error('vigencia_fim') is-invalid @enderror" 
                                    value="{{ old('vigencia_fim', $convencao->vigencia_fim ? $convencao->vigencia_fim->format('Y-m-d') : '') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Data-Base (Mês) <span class="text-danger">*</span></label>
                                <input type="text" name="data_base" class="form-control @error('data_base') is-invalid @enderror" 
                                    value="{{ old('data_base', $convencao->data_base) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-secondary">Abrangência Territorial e Econômica</label>
                                <textarea name="abrangencia" rows="3" class="form-control @error('abrangencia') is-invalid @enderror">{{ old('abrangencia', $convencao->abrangencia) }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="bg-light p-3 rounded border">
                                    <label class="form-label fw-bold small text-primary mb-1">
                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i> Documento Oficial da Convenção Completa (PDF)
                                    </label>
                                    
                                    @if($convencao->arquivo_pdf)
                                        <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded border mb-2">
                                            <div>
                                                <i class="fa-solid fa-file-pdf text-danger fa-lg me-2"></i>
                                                <a href="{{ route('admin.convencoes.download-pdf', $convencao) }}" target="_blank" class="fw-bold text-decoration-none text-dark small">
                                                    {{ $convencao->arquivo_nome_original ?: 'Arquivo Anexo da Convenção' }}
                                                </a>
                                                @if($convencao->arquivo_tamanho_formatado)
                                                    <span class="badge bg-secondary-subtle text-secondary small ms-1">{{ $convencao->arquivo_tamanho_formatado }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.convencoes.download-pdf', $convencao) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 small">
                                                    <i class="fa-solid fa-download me-1"></i> Baixar
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 small ms-1" onclick="confirmRemoverPdf()">
                                                    <i class="fa-solid fa-trash me-1"></i> Remover
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    <input type="file" name="arquivo_pdf" class="form-control form-control-sm @error('arquivo_pdf') is-invalid @enderror" accept=".pdf,application/pdf">
                                    <div class="form-text small text-muted">
                                        {{ $convencao->arquivo_pdf ? 'Envie um novo arquivo PDF caso deseje substituir o documento atual.' : 'Anexe a cópia integral da convenção assinada e registrada (PDF até 25MB).' }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" name="ativo" id="ativoCheck" value="1" {{ old('ativo', $convencao->ativo) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="ativoCheck">
                                        Convenção Ativa no Sistema
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="text-end">
                            <a href="{{ route('admin.convencoes.show', $convencao) }}" class="btn btn-light rounded-pill px-4 me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>

                    @if($convencao->arquivo_pdf)
                        <form id="formRemoverPdf" action="{{ route('admin.convencoes.remover-pdf', $convencao) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmRemoverPdf() {
        Swal.fire({
            title: 'Remover PDF da Convenção?',
            text: "O arquivo anexo será excluído do servidor.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formRemoverPdf').submit();
            }
        });
    }
</script>
@endpush
@endsection
