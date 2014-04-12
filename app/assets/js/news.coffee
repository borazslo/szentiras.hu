define ['jquery'], ->
  $(".clicktoopen").click (event) ->
      openable = $(event.target).parent().find "div.openit"
      if openable.is(":hidden")
        $(openable).slideDown "slow"
      else
        $(openable).slideUp "slow"