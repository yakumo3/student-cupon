$(function() {

	$('#input_group').submit(function(e) {

		//if invalid do nothing
		if(!$("#input_group").validationEngine('validate')){
			alert("未入力項目があります");
			return false;
		}

		// Ajax通信を開始する
		$.ajax({
				url: 'api.php',
				type: 'post',
				dataType: 'json',
				async: false,
				data: {
					name: $("input[name='name']").val(),
					mail: $("input[name='email']").val(),
					sex: $("input[name='sex']").val(),
					university: $("input[name='university']").val(),
					department: $("input[name='department']").val(),
					career: $("input[name='career']").val()
				}
			})
			// ・ステータスコードは正常で、dataTypeで定義したようにパース出来たとき
			.done(function(response) {
				if (response.data = 'error') {
					alert('登録済みのアドレスです。');
					alert(response.data);
				} else {
					alert("登録成功");
					alert(response.data);
				}
			})
			// ・サーバからステータスコード400などが返ってきたとき
			// ・ステータスコードは正常だが、dataTypeで定義したようにパース出来なかったとき
			// ・通信に失敗したとき
			.fail(function() {
				$('#result').html('失敗');
				alert("failed ajax");
			});

		return e.preventDefault();
	});

});
