
# methode de route pour les multyroute les routes 
 /**
     * Define the "chauffeurs" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapDossierRoutes()
    {
        Route::middleware('web')
            ->prefix('dossiers')
            ->namespace($this->namespace)
            ->group(base_path('routes/dossiers.php'));
    }


# code pour une annimation d'ajout 
	************HTML**************
	<div class="form-group form-group-last row">
		<div class="col-lg-12 form-group-sub">
			<label for="lieu" class="form-control-label"> document <span class="red">*</span></label>
			<div class="input-group" id="Fichier">
				<div class="input-group-prepend">
					<div class="input-group-prepend">
						<span class="input-group-text"><i class="far fa-folder-open"></i></span>
					</div>
					<input type="file" class="form-control" name="fichierDuDocument[]" id="lieu" placeholder="Renseignez le libellé du fichier">
					<button type="button" class="btn removeParticipant" style="background-color: transparent"><span class="fa fa-trash" style="color: #E84323; font-size: 18px;"></span></button>
				</div>
			</div>
			<button id="addFichier" type="button" class="btn btn-primary btn-lg" style=""><span class="fa fa-plus"></span> Ajouter un document</button>

		</div>
	</div>
	************js**************
	@push('scripts')
	     <script>
	    $('#addFichier').click(function () {
	        console.log('ici');
	        $('#Fichier').append('<div class="input-group-prepend">'
						+'<div class="input-group-prepend">'
						+ '<span class="input-group-text">'
						+'<i class="far fa-folder-open"></i>'
						+'</span>'
						+'</div>'
						+'<input type="file" class="form-control" name="fichierDuDocument[]" id="lieu" placeholder="Renseignez le libellé du fichier">'
						+ '<button type="button" class="btn removeParticipant" style="background-color: transparent">'
					    +'<span class="fa fa-trash" style="color: #E84323; font-size: 18px;">'
					    +'</span>'
						+'</button>'
						+'</div>');
	         });
	         $("#tableParticipants").on('click', '.removeParticipant', function ()
	            {
	                $(this).closest('tr').remove();
	            });
	</script>
	@endpush

# suppression avec les clé etrangere 
	 public function delete($id)
	    {
	    $action = Client::destroy($id)
	    if (!$action)
	        {
	            session()->flash('type', 'alert-success');
	            session()->flash('message', 'Erreur lors de la suppression du client');
	            return back();
	        }else{
	        	Incident::where(['client_id' => $id])->delete();
	            Sclient::where(['client_id' => $id])->delete();
	            Livraison::where(['client_id' => $id])->delete();

	            session()->flash('type', 'alert-success');
	            session()->flash('message', "Le client a été supprimé avec succès!");
	            return back();
	            
	        }
	    }



#############scripte de recherche #############
 public function search(Request $request){
        //dd($request);
        $data['menu'] = 'livraison-import';
        $data['livraisons'] = Livraison::with("client","sclient","navire","facture")->orderBy("id","DESC")->paginate(100);

        $data['typeconteneurs'] = Typeconteneur::orderBy('type')->get();
        $data['typelivraisons'] = Typelivraison::orderBy('name')->get();
        $data['statuts'] = ['enattente', 'encours', 'termine'];
        $data['clients'] = Client::whereHas('sclients')->orderBy('nom')->get();

        $search = $request->search ?? '';
        $client = $request->client ?? 'all';
        $clientOperateur = $client == 'all' ? '!=' : '=';
        $sclient = $request->sclient ?? 'all';
        $sclientOperateur = $sclient == 'all' ? '!=' : '=';
        $typeconteneur = $request->typeconteneur ?? 'all';
        $typeconteneurOperateur = $typeconteneur == 'all' ? '!=' : '=';
        $typelivraison = $request->typelivraison;
        $typelivraisonOperateur = ($typelivraison == 0) ? '!=' : '=';
        $mode = $request->mode ?? 'all';
        if($mode == 'rm') {
            $mode = 1;
        }
        elseif($request->mode == 'ac') {
            $mode = 0;
        }
        else {
            $mode = 3;
        }
        $modeOperateur = $mode == 3 ? '!=' : '=';
        $statut = $request->statut ?? 'all';
        $statutOperateur = $statut == 'all' ? '!=' : '=';
        $from = isset($request->from) ? date("Y-m-d 00:00:00", strtotime($request->from)) : date('Y-m-d', strtotime(0)); // 1970-01-01
        $to =  isset($request->to) ? date("Y-m-d 23:59:00", strtotime($request->to))  : date('Y-m-d', strtotime('+1 year'));

        $data['livraisons'] = Livraison::whereBetween('datelivraison',[$from,$to])
            ->where('typeconteneur_id', $typeconteneurOperateur, $typeconteneur)
            ->where('client_id', $clientOperateur, $client)
            ->where('sclient_id', $sclientOperateur, $sclient)
            ->where('typelivraison_id', $typelivraisonOperateur, $typelivraison)
            ->where('modelivraison', $modeOperateur, $mode)
            ->where('statut', $statutOperateur, $statut)
            ->where(function($q) use($search) {
                $q->where('reference', 'LIKE', "%$search%")
                    ->orWhere('numctn', 'LIKE', "%$search%")
                    ->orWhere('numdeclaration', 'LIKE', "%$search%")
                    ->orWhere('bl', 'LIKE', "%$search%");
            })
            ->orderBy('datelivraison','DESC')
            ->paginate('1000');

        //return response()->json($data['livraisons']);
        //dd($data['livraisons']);
        $data['livraisons']->appends([
            'search' => $request->search,
            'client' => $request->client,
            'sclient' => $request->sclient,
            'typeconteneur' => $request->typeconteneur,
            'typelivraison' => $request->typelivraison,
            'mode' => $request->mode,
            'statut' => $request->statut,
            'from' => $request->from,
            'to' => $request->to,
        ]);

        if($request->impression == "on"){
            $pdf = PDF::loadView('livraisons.print', $data )->setPaper('a4', 'landscape');
            $name = "Liste-des-livraisons.pdf";
            return $pdf->stream($name);
        }

        return view('livraisons.index', $data);
    }

############# generer un mot de passe ############
function generatePassword($length = 6) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }
        return $password;
    }

############### filtre ###############""

public function printListe(Request $request)
    {
        $name = $request->name ?? '';
        $typecontrat = $request->typecontrat ?? 'all';
        $typecontratOperator = $typecontrat == 'all' ? '!=' : '=';
        $statutmatrimonial = $request->statutmatrimonial ?? 'all';
        $statutmatrimonialOperator = $statutmatrimonial == 'all' ? '!=' : '=';
        $from = $request->from ?? date('Y-m-d', strtotime(0)); // 1970-01-01
        $to = Carbon::parse($request->to)->addDay();
        $enfantsOperator = $request->has('enfants') ? '>' : '>=';

        $chauffeurs = Chauffeur::where('categorie', $categorieOperator, $categorie)
            ->where('typecontrat', $typecontratOperator, $typecontrat)
            ->where('statutmatri', $statutmatrimonialOperator, $statutmatrimonial)
            ->whereBetween('dateprisefonction', [$from, $to])
            ->where('nbenfants', $enfantsOperator, 0)
            ->where(function($q) use($name) {
                $q->where('nom', 'LIKE', "%$name%")->orWhere('prenoms', 'LIKE', "%$name%");
            })
            ->orderBy('nom')
            ->get();

        $data['chauffeurs'] = $chauffeurs;

        //return view('clients.print', $data);
        $pdf = PDF::loadView('chauffeurs.print', $data )->setPaper('a4');
        $name = "Liste-clients.pdf";
        User::logs("Impression de la liste des chauffeurs ");
        return $pdf->stream($name);
    }