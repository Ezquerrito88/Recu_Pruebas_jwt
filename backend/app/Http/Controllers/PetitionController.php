<?php

namespace App\Http\Controllers;

use App\Models\Petitions;
use App\Models\Categories;
use App\Models\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class PetitionController extends Controller
{
    private function sendResponse($data, $message, $code = 200) {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message
        ], $code);
    }

    private function sendError($error, $errorMessages = [], $code = 404) {
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }
        return response()->json($response, $code);
    }

    public function index()
    {
        try {
            $petitions = Petitions::with(['user', 'categoria', 'files'])->get();
            return $this->sendResponse($petitions, 'Peticiones recuperadas con éxito');
        } catch (Exception $e) {
            return $this->sendError('Error al recuperar peticiones', $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $petition = Petitions::with(['user', 'categoria', 'files'])->findOrFail($id);
            return $this->sendResponse($petition, 'Petición encontrada');
        } catch (Exception $e) {
            return $this->sendError('Petición no encontrada', [], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo'       => 'required|max:255',
            'descripcion'  => 'required',
            'destinatario' => 'required',
            'categoria_id' => 'required|exists:categories,id',
            'file'         => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error de validación', $validator->errors(), 422);
        }

        try {
            if ($file = $request->file('file')) {
                $path = $file->store('peticiones', 'public');

                $petition = new Petitions($request->all());
                $petition->user_id = Auth::id();
                $petition->firmantes = 0;
                $petition->estado = 'pendiente';
                $petition->save();


                $petition->files()->create([
                    'name' => $file->getClientOriginalName(),
                    'file_path' => $path
                ]);

                return $this->sendResponse($petition->load('files'), 'Petición creada con éxito', 201);
            }
            return $this->sendError('El archivo es obligatorio', [], 422);

        } catch (Exception $e) {
            return $this->sendError('Error al crear la petición', $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $petition = Petitions::findOrFail($id);
            

            if ($request->user()->cannot('update', $petition)) {
                return $this->sendError('No autorizado', [], 403);
            }

            $petition->update($request->all());
            return $this->sendResponse($petition, 'Petición actualizada con éxito');
        } catch (Exception $e) {
            return $this->sendError('Error al actualizar', $e->getMessage(), 500);
        }
    }


    public function destroy(Request $request, $id)
    {
        try {
            $petition = Petitions::findOrFail($id);

            if ($request->user()->cannot('delete', $petition)) {
                return $this->sendError('No autorizado', [], 403);
            }

            foreach ($petition->files as $file) {
                Storage::disk('public')->delete($file->file_path);
            }

            $petition->delete();
            return $this->sendResponse(null, 'Petición eliminada con éxito');
        } catch (Exception $e) {
            return $this->sendError('Error al eliminar', $e->getMessage(), 500);
        }
    }

    public function listMine()
    {
        try {
            $user = Auth::user();
            $petitions = Petitions::where('user_id', $user->id)
                ->with(['user', 'categoria', 'files'])->get();
            return $this->sendResponse($petitions, 'Tus peticiones recuperadas con éxito');
        } catch (Exception $e) {
            return $this->sendError('Error al recuperar tus peticiones', $e->getMessage(), 500);
        }
    }

    public function firmar(Request $request, $id)
    {
        try {
            $petition = Petitions::findOrFail($id);
            $user = Auth::user();

            if ($petition->firmas()->where('user_id', $user->id)->exists()) {
                return $this->sendError('Ya has firmado esta petición', [], 403);
            }

            $petition->firmas()->attach($user->id);
            $petition->increment('firmantes');
            
            return $this->sendResponse($petition, 'Petición firmada con éxito', 201);
        } catch (Exception $e) {
            return $this->sendError('No se pudo firmar la petición', $e->getMessage(), 500);
        }
    }
}