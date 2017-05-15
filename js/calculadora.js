
$(document).ready(function() {

  /* block all keys except 0 and 1*/
  $("#numeroBinario").keypress( function(e) {
    var chr = String.fromCharCode(e.which);
    if ("01".indexOf(chr) < 0) {
      code = e.which;
      if (code == 0 || code == 8) {
        return true;
      };

      return false;

     }

    return true;
  });

  /* only numbers keys */
  $(".only-numbers").keypress( function(e) {

    var chr = String.fromCharCode(e.which);
    code = e.which;

    if ("01234567890".indexOf(chr) < 0) {
      if (code == 0 || code == 8) {
        return true;
      };

      return false;
    };

    return true

  });

  $("#submitBinarioDecimal").click(function(event) {
    event.preventDefault();
    var binario = $('#numeroBinario').val();
    var decimal = $('#numeroDecimal').val();
    $.post("/php/main.php",{numeroBinario: binario, numeroDecimal: decimal }, function(data) {
      $('#results').html(data);
    })
  });

  $("#submitSubnetCalculator").click(function(event) {
    event.preventDefault();
    var ipValue = $('#ip').val();
    var maskValue = $('#mask').val();
    $.post('/php/main.php',{ip: ipValue, cidr: maskValue }, function(data) {
      $('#results').html(data);
    });
  });

  $("#submitClassIp").click(function(event) {
    event.preventDefault();
    var classIpValue = $('#ipClass').val();
    $.post('/php/main.php',{ classIp: classIpValue }, function(data) {
      $('#results').html(data);
    });
  });

});