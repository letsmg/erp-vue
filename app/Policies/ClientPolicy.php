<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
{
    /**
     * Determine whether the user can view any clients.
     */
    public function viewAny(User $user): Response
    {
        // Apenas admin e operator podem ver clientes
        return $user->isAdmin() || $user->isOperator()
            ? Response::allow()
            : Response::deny('Apenas administradores e operadores podem visualizar clientes.');
    }

    /**
     * Determine whether the user can view the client.
     */
    public function view(User $user, Client $client): Response
    {
        // Admin e operator podem ver qualquer cliente
        if ($user->isAdmin() || $user->isOperator()) {
            return Response::allow();
        }

        // Cliente pode ver seu próprio cadastro
        if ($user->isClient() && $client->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Você não tem permissão para visualizar este cliente.');
    }

    /**
     * Determine whether the user can create clients.
     */
    public function create(User $user): Response
    {
        // Apenas admin e operator podem criar clientes
        return $user->isAdmin() || $user->isOperator()
            ? Response::allow()
            : Response::deny('Apenas administradores e operadores podem cadastrar clientes.');
    }

    /**
     * Determine whether the user can update the client.
     */
    public function update(User $user, Client $client): Response
    {
        // Admin e operator podem editar qualquer cliente
        if ($user->isAdmin() || $user->isOperator()) {
            return Response::allow();
        }

        // Cliente pode editar seu próprio cadastro (campos limitados)
        if ($user->isClient() && $client->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Você não tem permissão para editar este cliente.');
    }

    /**
     * Determine whether the user can delete the client.
     */
    public function delete(User $user, Client $client): Response
    {
        // Apenas admin pode deletar clientes
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Apenas administradores podem deletar clientes.');
    }

    /**
     * Determine whether the user can toggle client status.
     */
    public function toggleStatus(User $user, Client $client): Response
    {
        // Apenas admin e operator podem ativar/desativar
        return $user->isAdmin() || $user->isOperator()
            ? Response::allow()
            : Response::deny('Apenas administradores e operadores podem ativar/desativar clientes.');
    }

    /**
     * Determine whether the user can view client fiscal information.
     */
    public function viewFiscalInfo(User $user, Client $client): Response
    {
        // Apenas admin e operator podem ver informações fiscais
        if ($user->isAdmin() || $user->isOperator()) {
            return Response::allow();
        }

        // Cliente pode ver suas próprias informações fiscais
        if ($user->isClient() && $client->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Você não tem permissão para visualizar informações fiscais deste cliente.');
    }

    /**
     * Determine whether the user can export client data.
     */
    public function export(User $user): Response
    {
        // Apenas admin pode exportar dados de clientes
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Apenas administradores podem exportar dados de clientes.');
    }
}
